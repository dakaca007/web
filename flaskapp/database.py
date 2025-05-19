from sqlalchemy import create_engine, Column, Integer, String, Text, DateTime
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from datetime import datetime
import os

DB_URI = "mysql+pymysql://sujiangxi:U4JcgUOkcHMI1suU@mysql.sqlpub.com:3306/mysql_app?charset=utf8mb4&ssl_ca=/path/to/ca.pem"
engine = create_engine(DB_URI, pool_recycle=3600, pool_pre_ping=True)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

Base = declarative_base()

class User(Base):
    __tablename__ = "users"
    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(50), unique=True, index=True)
    password_hash = Column(String(128))
    email = Column(String(100))
    created_at = Column(DateTime, default=datetime.now)

class Message(Base):
    __tablename__ = "messages"
    id = Column(Integer, primary_key=True, index=True)
    content = Column(Text)
    user_id = Column(Integer)
    created_at = Column(DateTime, default=datetime.now)

# 创建表（如果不存在）
Base.metadata.create_all(bind=engine)